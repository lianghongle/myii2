// **********************************************************************
// This file was generated by a TAF parser!
// TAF version 3.0.0.29 by WSRD Tencent.
// Generated from `chat.jce'
// **********************************************************************

package ld.Bu;

public final class MsgType implements java.io.Serializable
{
    private static MsgType[] __values = new MsgType[3];
    private int __value;
    private String __T = new String();

    public static final int _TEXT = 1;
    public static final MsgType TEXT = new MsgType(0,_TEXT,"TEXT");
    public static final int _VOICE = 2;
    public static final MsgType VOICE = new MsgType(1,_VOICE,"VOICE");
    public static final int _PIC = 3;
    public static final MsgType PIC = new MsgType(2,_PIC,"PIC");

    public static MsgType convert(int val)
    {
        for(int __i = 0; __i < __values.length; ++__i)
        {
            if(__values[__i].value() == val)
            {
                return __values[__i];
            }
        }
        assert false;
        return null;
    }

    public static MsgType convert(String val)
    {
        for(int __i = 0; __i < __values.length; ++__i)
        {
            if(__values[__i].toString().equals(val))
            {
                return __values[__i];
            }
        }
        assert false;
        return null;
    }

    public int value()
    {
        return __value;
    }

    public String toString()
    {
        return __T;
    }

    private MsgType(int index, int val, String s)
    {
        __T = s;
        __value = val;
        __values[index] = this;
    }

}